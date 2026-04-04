package com.sieuthithucung.service.impl;

import com.sieuthithucung.dto.OrderItemDto;
import com.sieuthithucung.entity.OrderItemEntity;
import com.sieuthithucung.exception.ResourceNotFoundException;
import com.sieuthithucung.mapper.OrderItemMapper;
import com.sieuthithucung.repository.OrderItemRepository;
import com.sieuthithucung.service.OrderItemService;
import org.springframework.beans.BeanUtils;
import org.springframework.beans.BeanWrapper;
import org.springframework.beans.BeanWrapperImpl;
import org.springframework.stereotype.Service;

import java.beans.PropertyDescriptor;
import java.util.HashSet;
import java.util.List;
import java.util.Set;

@Service
public class OrderItemServiceImpl implements OrderItemService {

    private final OrderItemRepository repository;

    public OrderItemServiceImpl(OrderItemRepository repository) {
        this.repository = repository;
    }
    @Override
    public OrderItemDto create(OrderItemDto dto) {
        return createInternal(dto);
    }

    @Override
    public OrderItemDto update(Long id, OrderItemDto dto) {
        return updateInternal(id, dto);
    }

    @Override
    public void delete(Long id) {
        if (!repository.existsById(id)) {
            throw new ResourceNotFoundException("Order item not found with id: " + id);
        }
        repository.deleteById(id);
    }

    @Override
    public OrderItemDto findById(Long id) {
        return findByIdInternal(id);
    }

    @Override
    public List<OrderItemDto> getAll() {
        return getAllInternal();
    }

    private OrderItemDto createInternal(OrderItemDto dto) {
        OrderItemEntity entity = OrderItemMapper.mapToOrderItemEntity(dto);
        OrderItemEntity saved = repository.save(entity);
        return OrderItemMapper.mapToOrderItemDto(saved);
    }

    private OrderItemDto updateInternal(Long id, OrderItemDto dto) {
        OrderItemEntity existing = repository.findById(id)
                .orElseThrow(() -> new ResourceNotFoundException("Order item not found with id: " + id));

        OrderItemEntity source = OrderItemMapper.mapToOrderItemEntity(dto);
        BeanUtils.copyProperties(source, existing, getNullPropertyNames(source));

        OrderItemEntity saved = repository.save(existing);
        return OrderItemMapper.mapToOrderItemDto(saved);
    }

    private OrderItemDto findByIdInternal(Long id) {
        OrderItemEntity entity = repository.findById(id)
                .orElseThrow(() -> new ResourceNotFoundException("Order item not found with id: " + id));
        return OrderItemMapper.mapToOrderItemDto(entity);
    }

    private List<OrderItemDto> getAllInternal() {
        return repository.findAll().stream().map(OrderItemMapper::mapToOrderItemDto).toList();
    }

    private String[] getNullPropertyNames(Object source) {
        BeanWrapper src = new BeanWrapperImpl(source);
        PropertyDescriptor[] pds = src.getPropertyDescriptors();

        Set<String> emptyNames = new HashSet<>();
        for (PropertyDescriptor pd : pds) {
            Object srcValue = src.getPropertyValue(pd.getName());
            if (srcValue == null) {
                emptyNames.add(pd.getName());
            }
        }
        return emptyNames.toArray(new String[0]);
    }
}