package com.sieuthithucung.service.impl;

import com.sieuthithucung.dto.ContactDto;
import com.sieuthithucung.entity.ContactEntity;
import com.sieuthithucung.exception.ResourceNotFoundException;
import com.sieuthithucung.mapper.ContactMapper;
import com.sieuthithucung.repository.ContactRepository;
import com.sieuthithucung.service.ContactService;
import org.springframework.beans.BeanUtils;
import org.springframework.beans.BeanWrapper;
import org.springframework.beans.BeanWrapperImpl;
import org.springframework.stereotype.Service;

import java.beans.PropertyDescriptor;
import java.util.HashSet;
import java.util.List;
import java.util.Set;

@Service
public class ContactServiceImpl implements ContactService {

    private final ContactRepository repository;

    public ContactServiceImpl(ContactRepository repository) {
        this.repository = repository;
    }
    @Override
    public ContactDto create(ContactDto dto) {
        return createInternal(dto);
    }

    @Override
    public ContactDto update(Long id, ContactDto dto) {
        return updateInternal(id, dto);
    }

    @Override
    public void delete(Long id) {
        if (!repository.existsById(id)) {
            throw new ResourceNotFoundException("Contact not found with id: " + id);
        }
        repository.deleteById(id);
    }

    @Override
    public ContactDto findById(Long id) {
        return findByIdInternal(id);
    }

    @Override
    public List<ContactDto> getAll() {
        return getAllInternal();
    }

    private ContactDto createInternal(ContactDto dto) {
        ContactEntity entity = ContactMapper.mapToContactEntity(dto);
        ContactEntity saved = repository.save(entity);
        return ContactMapper.mapToContactDto(saved);
    }

    private ContactDto updateInternal(Long id, ContactDto dto) {
        ContactEntity existing = repository.findById(id)
                .orElseThrow(() -> new ResourceNotFoundException("Contact not found with id: " + id));

        ContactEntity source = ContactMapper.mapToContactEntity(dto);
        BeanUtils.copyProperties(source, existing, getNullPropertyNames(source));

        ContactEntity saved = repository.save(existing);
        return ContactMapper.mapToContactDto(saved);
    }

    private ContactDto findByIdInternal(Long id) {
        ContactEntity entity = repository.findById(id)
                .orElseThrow(() -> new ResourceNotFoundException("Contact not found with id: " + id));
        return ContactMapper.mapToContactDto(entity);
    }

    private List<ContactDto> getAllInternal() {
        return repository.findAll().stream().map(ContactMapper::mapToContactDto).toList();
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